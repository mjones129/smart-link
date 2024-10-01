import { Button, Icons } from "@wordpress/components";
import { useBlockProps } from "@wordpress/blocks";
import { mergestyles } from "@wordpress/components";

const MyCustomComponent = () => {
  const blockProps = useBlockProps();
  const buttonStyle = { backgroundColor: "blue", color: "white" };

  return (
    <div {...blockProps}>
      <Button className="my-custom-button" style={mergestyles(buttonStyle)}>
        <Icons icon="upload" /> My Custom Button
      </Button>
    </div>
  );
};
